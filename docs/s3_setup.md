# Setting Up Uploads To an Amazon S3 Bucket

## Create bucket

* Through the AWS S3 console, create a bucket that will hold uploaded files for the instance (i.e. 'plagmada_staging'). 

* Name the bucket accordingly. For example, for staging server: `plagmada_staging`

## Create an IAM user to access the bucket

(So that we can isolate access to the bucket only)

* Create a user specifically for the bucket through the AWS IAM console. Name it something accordingly (e.g. going with the bucket example above, `plagmada_curator_usr`)

* Create a security policy that you will attach to the user to provide access to the bucket. 
    - On the IAM console, go to Policies > Create Policy > Policy Generator
    - Use the tool to generate the policy. You can use the following article to help with what the policy should contain:
        + http://blogs.aws.amazon.com/security/post/Tx3VRSWZ6B3SHAV/Writing-IAM-Policies-How-to-grant-access-to-an-Amazon-S3-bucket

```
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "Stmt1457628622000",
            "Effect": "Allow",
            "Action": [
                "s3:DeleteObject",
                "s3:GetObject",
                "s3:PutObject"
            ],
            "Resource": [
                "arn:aws:s3:::plagmada_staging/*"
            ]
        },
        {
            "Sid": "Stmt1457628963000",
            "Effect": "Allow",
            "Action": [
                "s3:ListBucket"
            ],
            "Resource": [
                "arn:aws:s3:::plagmada_staging"
            ]
        }
    ]
}
```

* Name the policy something appropriate, such as `plagmada_staging-rw`

* Don't forget to attach the policy to the IAM user in the "Permissions" tab on the user detail view.

## Setup

### Install S3FS on the instance app server(s)

Without S3FS, the app server will typically be configured to store upload files on some local directory (usually in `/var/www/omeka/files`, (and /var/www/omeka is a linked directory)).

Now we will use S3FS to mount a FUSE folder that maps to our S3 bucket, and configure the app server to store uploads there.

### Download and install S3FS

See the https://github.com/s3fs-fuse/s3fs-fuse/wiki/Installation-Notes

* Put the S3FS source in /usr/local/src/ on the app server
* Build according to install notes on the above link
* Create the directory that will be the mount point for the uploads bucket
    - e.g. `/var/www/plagmada_staging`
    - If the directory already exists, make sure it is empty. Otherwise, rename and replace.
* Create /etc/passwd-s3fs with the IAM credentials set up previously. Pay attention to the notes on file permissions for /etc/passwd-s3fs at https://github.com/s3fs-fuse/s3fs-fuse/wiki/Fuse-Over-Amazon
